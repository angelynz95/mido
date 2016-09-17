package com.cia.mido;

import android.content.Intent;
import android.support.v7.app.ActionBar;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.ListView;
import android.widget.TextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

import it.gmariotti.cardslib.library.internal.Card;
import it.gmariotti.cardslib.library.internal.CardArrayAdapter;
import it.gmariotti.cardslib.library.internal.CardHeader;
import it.gmariotti.cardslib.library.internal.CardThumbnail;
import it.gmariotti.cardslib.library.view.CardListView;
import it.gmariotti.cardslib.library.view.CardView;

public class NewsActivity extends AppCompatActivity {
    private ArrayList<Card> cards = new ArrayList<Card>()
            ;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_news);
        showNews();
    }

    @Override
    public void onBackPressed() {
        Intent menuIntent = new Intent(this, MenuActivity.class);
        startActivity(menuIntent);
        finish();
    }

    public void chooseSocialManager(View view) {
        Intent socialMediaIntent = new Intent(this, SocialMediaActivity.class);
        startActivity(socialMediaIntent);
        finish();
    }

    public void chooseNews(View view) {
        Intent newsIntent = new Intent(this, NewsActivity.class);
        startActivity(newsIntent);
        finish();
    }

    public void chooseMarketInsight(View view) {

    }

    public void chooseEmployeeSearch(View view) {

    }

    private void showNews() {
        String url = getResources().getString(R.string.backEndUrl) + getResources().getString(R.string.newsUrl);
        Client client = new Client(url, null, "GET");
        client.execute();

        JSONObject response;
        List<String> newsList = new ArrayList<>();
        try {
            do {
                Thread.sleep(1000);
                response = client.getResponse();
            } while (response == null);

            JSONArray newsJs = (JSONArray) response.get("news");
            for (int i = 0; i < newsJs.length(); i++) {
                JSONObject news = (JSONObject) newsJs.get(i);
                String imageUrl = news.getString("image_url");
                String time = news.getString("time");
                String title = news.getString("title");
                String header = news.getString("header");
                String readMore = news.getString("read_more");

                Card card = new Card(this);
                CardHeader headerCard = new CardHeader(this);
                headerCard.setTitle(title);
                card.setTitle(header);
                card.addCardHeader(headerCard);

                // Thumbnail
                CardThumbnail thumbnailCard = new CardThumbnail(this);
                thumbnailCard.setUrlResource(imageUrl);
//                thumbnailCard.setDrawableResource(getResources(R.mipmap.ic_launcher));
                card.addCardThumbnail(thumbnailCard);
                cards.add(card);
            }
        } catch (Exception e) {
            e.printStackTrace();
        }

        CardArrayAdapter mCardArrayAdapter = new CardArrayAdapter(this, cards);
        CardListView listView = (CardListView) this.findViewById(R.id.carddemo);
        if (listView != null) {
            listView.setAdapter(mCardArrayAdapter);
        }
    }
}
