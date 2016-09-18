package com.cia.mido;

import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.view.View;
import android.widget.Button;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

import it.gmariotti.cardslib.library.internal.Card;

/**
 * Created by LENOVO on 9/18/2016.
 */
public class EmployeeSearchActivity extends AppCompatActivity implements View.OnClickListener {
    private ArrayList<Card> cards = new ArrayList<Card>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        Button searchbtn = (Button) findViewById(R.id.searchJobButton);
        searchbtn.setOnClickListener(this);
        setContentView(R.layout.activity_employee_search);
    }

    @Override
    public void onBackPressed() {
        Intent menuIntent = new Intent(this, MenuActivity.class);
        startActivity(menuIntent);
        finish();
    }


    @Override
    public void onClick(View v) {
        switch (v.getId()) {
            case R.id.searchJobButton:
                String url = getResources().getString(R.string.backEndUrl) + getResources().getString(R.string.searchEmployeeUrl);
                Client client = new Client(url, null, "GET");
                client.execute();

                JSONObject response;
                List<String> employeeList = new ArrayList<>();
                try {
                    do {
                        Thread.sleep(1000);
                        response = client.getResponse();
                    } while (response == null);

                    String name = response.getString("name");
                    String link = response.getString("link");
                    String job = response.getString("job");
                    String bio = response.getString("bio");

                    System.out.println(name + " " + link);

                } catch (InterruptedException e) {
                    e.printStackTrace();
                } catch (JSONException e) {
                    e.printStackTrace();
                }
                break;
            default:
                break;
        }
    }
}
