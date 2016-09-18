package com.cia.mido;

import android.app.DownloadManager;
import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

import it.gmariotti.cardslib.library.internal.Card;
import it.gmariotti.cardslib.library.internal.CardArrayAdapter;
import it.gmariotti.cardslib.library.internal.CardHeader;
import it.gmariotti.cardslib.library.internal.CardThumbnail;
import it.gmariotti.cardslib.library.view.CardListView;

/**
 * Created by LENOVO on 9/18/2016.
 */
public class EmployeeSearchActivity extends AppCompatActivity {
    private ArrayList<Card> cards = new ArrayList<Card>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_employee_search);
    }

    @Override
    public void onBackPressed() {
        Intent menuIntent = new Intent(this, MenuActivity.class);
        startActivity(menuIntent);
        finish();
    }

    public void showSearchResult(View view) {
        EditText keywordField = (EditText) findViewById(R.id.keyword);
        String keyword = keywordField.getText().toString();

        requestToServer(keyword);
        CardArrayAdapter mCardArrayAdapter = new CardArrayAdapter(this, cards);
        CardListView listView = (CardListView) this.findViewById(R.id.employee_search);
        if (listView != null) {
            listView.setAdapter(mCardArrayAdapter);
        }
    }

    private void requestToServer(String keyword) {
        String url = getResources().getString(R.string.backEndUrl) + getResources().getString(R.string.searchEmployeeUrl);
        JSONObject request = new JSONObject();
        try {
            request.put("keyword", keyword);
        } catch (JSONException e) {
            e.printStackTrace();
        }
        Client client = new Client(url, request, "POST");
        client.execute();

        JSONObject response;
        try {
            do {
                Thread.sleep(1000);
                response = client.getResponse();
            } while (response == null);

            String name = response.getString("name");
            String link = response.getString("link");
            String job = response.getString("job");
            String bio = response.getString("bio");

            // Body
            String body = "Nama: " + name + "\nPranala: Rp"
                    + link + ",00\nPekerjaan: Rp" + job
                    + "\nBiografi: " + bio + "\n";

            // Card
            Card card = new Card(this);
            CardHeader headerCard = new CardHeader(this);
            headerCard.setTitle(name);
            card.setTitle(body);
            card.addCardHeader(headerCard);

            // Thumbnail
            CardThumbnail thumbnailCard = new CardThumbnail(this);
            card.addCardThumbnail(thumbnailCard);
            cards.add(card);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}